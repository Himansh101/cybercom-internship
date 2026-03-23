import { useRef, useState } from 'react';
import { useQueryClient } from '@tanstack/react-query';
import { api } from '../../services/api.js';

/**
 * AdminUpload lets admins upload a CSV and trigger Solr indexing.
 */
export default function AdminUpload() {
  const inputRef = useRef(null);
  const queryClient = useQueryClient();
  const [file, setFile] = useState(null);
  const [isUploading, setIsUploading] = useState(false);
  const [status, setStatus] = useState({ type: '', message: '' });

  const resetPicker = () => {
    if (inputRef.current) {
      inputRef.current.value = '';
    }
    setFile(null);
  };

  const handleUpload = async () => {
    if (!file || isUploading) {
      return;
    }

    setIsUploading(true);
    setStatus({ type: '', message: '' });

    try {
      const res = await api.uploadCsv(file);
      const imported = res.data?.imported ?? 0;
      const sourceFile = res.data?.source_file ?? file.name;

      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['schema'] }),
        queryClient.invalidateQueries({ queryKey: ['report'] }),
        queryClient.invalidateQueries({ queryKey: ['chart'] }),
      ]);

      setStatus({
        type: 'success',
        message: `${imported} rows indexed from ${sourceFile}.`,
      });
      resetPicker();
    } catch (error) {
      setStatus({
        type: 'error',
        message: error.message ?? 'Upload failed',
      });
    } finally {
      setIsUploading(false);
    }
  };

  return (
    <div className="flex items-center gap-2 text-xs">
      <input
        ref={inputRef}
        type="file"
        accept=".csv,text/csv"
        className="hidden"
        onChange={(e) => {
          const nextFile = e.target.files?.[0] ?? null;
          setFile(nextFile);
          setStatus({ type: '', message: '' });
        }}
      />

      <button
        type="button"
        className="btn-secondary text-xs"
        onClick={() => inputRef.current?.click()}
      >
        Choose CSV
      </button>

      <button
        type="button"
        className={`btn-primary text-xs ${!file || isUploading ? 'opacity-60' : ''}`}
        onClick={handleUpload}
        disabled={!file || isUploading}
      >
        {isUploading ? 'Indexing...' : 'Upload & Index'}
      </button>

      <span className="max-w-40 truncate text-gray-500" title={file?.name ?? ''}>
        {file?.name ?? 'No file selected'}
      </span>

      {status.message && (
        <span className={status.type === 'error' ? 'text-red-600' : 'text-green-600'}>
          {status.message}
        </span>
      )}
    </div>
  );
}
