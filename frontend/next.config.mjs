/** @type {import('next').NextConfig} */
const nextConfig = {
  // Allow images from picsum.photos (used by product seeders)
  images: {
    remotePatterns: [
      {
        protocol: 'https',
        hostname: 'picsum.photos',
      },
    ],
  },
}

export default nextConfig
